select
d.key_id,
d.name,
(SELECT COUNT(*) FROM principal_investigator_department pd WHERE  pd.department_id = d.key_id) as piCount,
(SELECT COUNT(*) FROM principal_investigator_room pr WHERE principal_investigator_id 
	IN(
		select principal_investigator_id 
		from principal_investigator_department 
		where department_id=d.key_id
	)
) as roomCount
from department d
GROUP BY d.key_id;
